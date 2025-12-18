export default function Loading() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-indigo-50">
      <div className="text-center">
        <div className="inline-block relative w-20 h-20">
          <div className="absolute border-4 border-blue-200 rounded-full w-20 h-20"></div>
          <div className="absolute border-4 border-blue-600 rounded-full w-20 h-20 animate-spin border-t-transparent"></div>
        </div>
        <p className="mt-4 text-gray-600 font-medium">Chargement...</p>
      </div>
    </div>
  );
}
